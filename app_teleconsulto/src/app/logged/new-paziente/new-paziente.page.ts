import { Component } from '@angular/core';
import { NavigationExtras, Router } from '@angular/router';
import { Paziente } from 'src/app/models/paziente';
import { AccessProviders } from 'src/app/providers/access-providers';

@Component({
  selector: 'app-new-paziente',
  templateUrl: './new-paziente.page.html',
  styleUrls: ['./new-paziente.page.scss'],
})
export class NewPazientePage {

  pazienti: any = [];
  paziente_cognome: string = "";
  paziente_nome: string = "";
  paziente_cf: string = "";

  constructor(
    private router: Router,
    private accessProviders: AccessProviders
  ) { }

  async searchPaziente() {
    this.pazienti = [];
    return new Promise(resolve => {
      let body = {
        paziente_cognome: this.paziente_cognome,
        paziente_nome: this.paziente_nome,
        paziente_cf: this.paziente_cf
      }

      this.accessProviders.postData(body, 'search_paziente').subscribe((res: any) => {
        for (let datas of res.result) {
          this.pazienti.push(datas);
        }
        resolve(true);
      })
    })
  }

  addPaziente(paziente: any) {
    let navigationExtras: NavigationExtras = {
      queryParams: {
        paziente: JSON.stringify(paziente),
      }
    };
    this.router.navigate(['/nuovo'], navigationExtras);
  }

}
